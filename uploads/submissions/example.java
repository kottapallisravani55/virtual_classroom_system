import java.util.*;
class array{
public static void main(String args[]){
Scanner sc=new Scanner(System.in);
System.out.println("enter size of the array:");
int size=sc.nextInt();
System.out.println("enter elements");
int a[]=new int[size];
int i,j,flag=0;
for(i=0;i<size;i++){
a[i]=sc.nextInt();
}
System.out.println("enter target element");
int target=sc.nextInt();
System.out.println("all possible indexes");
for(i=0;i<size;i++){
for(j=0;j<size;j++){
if((a[i]+a[j])==target){

System.out.println("position 1: "+ i +" position 2: "+j);
}
else{
flag=1;
}
}
}
if(flag==1){
System.out.println("not possible to add");
}
}
}